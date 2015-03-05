<?php

namespace Zk2\Bundle\UsefulBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UsefulFormController extends Controller
{
    /**
     * DependentEntity
     */
    public function dependentEntityAction()
    {
        $request = $this->get('request')->request;
        $translator = $this->get('translator');
	
	$em_name    = $request->get('em_name');
        $em         = $this->get('doctrine')->getManager( $em_name );
	
        $parent_id        = $request->get('parent_id');
        $empty_value      = $request->get('empty_value');
	$class            = $request->get('class');
	$parent_field     = $request->get('parent_field');
	$property         = $request->get('property');
	$order_property   = $request->get('order_property');
	$order_direction  = $request->get('order_direction');
	$no_result_msg    = $request->get('no_result_msg');
	
	if( !$query = $request->get('query') )
	{
	    $query = sprintf(
		"SELECT e.id,e.%s FROM %s e WHERE e.id<>0 ",
		$property, $class
	    );
	    $rootAlias = 'e';
	}
	else
	{
	    $query = urldecode( $query );
	    $rootAlias = substr($query, 7, 1);
	}
	
	$query .= sprintf(
	    " AND %s.%s='%s' ORDER BY %s.%s %s",
	    $rootAlias, $parent_field, $parent_id, $rootAlias, $order_property, $order_direction
	);
	
	$results = $em->createQuery( $query )
	    ->getScalarResult()
	;
	$html = '';
	
        if (empty($results))
	{
            return new Response('<option value="">' . $translator->trans($no_result_msg) . '</option>');
        }
	
        if ($empty_value)
	{
	    $html .= '<option value="">' . $translator->trans($empty_value) . '</option>';
	}
        
        foreach($results as $result)
        {
            $html .= sprintf("<option value=\"%d\">%s</option>",$result['id'], $result[$property]);
        }
        return new Response($html);
    }
    
    /**
     * EntityAjaxAutocomplete
     */
    public function entityAjaxAutocompleteAction()
    {
        $request = $this->get('request');
	$em_name = $request->get('em_name');
        $em = $this->get('doctrine')->getManager( $em_name );
	$res = array();

        if( $class = $request->get('class') )
	{
	    $property = $request->get('property');
	    $prop = $request->get('prop');
	    $condition_operator = $request->get('condition_operator');
	    $max_rows = $request->get('max_rows');
	    
	    switch ($condition_operator)
	    {
		case "begins_with":
                    $like = $prop . '%';
                    break;
		case "ends_with":
                    $like = '%' . $prop;
                    break;
		case "contains":
                    $like = '%' . $prop . '%';
                    break;
                default:
                    throw new \Exception('Unexpected value of parameter "condition_operator"'); 
	    }
	    
	    if( !$query = $request->get('query') )
	    {
		$query = sprintf( "SELECT e.id,e.%s FROM %s e WHERE e.id>0 ", $property, $class );
		$rootAlias = 'e';
	    }
	    else
	    {
		$query = urldecode( $query );
		$rootAlias = substr($query, 7, 1);
	    }
	    
	    $query .= sprintf( " AND LOWER(%s.%s) LIKE LOWER(:like) ", $rootAlias, $property );
	    
	    $results = $em->createQuery( $query )
                ->setParameter( 'like', $like )
                ->setMaxResults( $max_rows )
                ->getScalarResult()
	    ;
	    
	    foreach( $results as $r )
	    {
                $res[] = array( 'id' => $r['id'], 'name' => $r[$property] );
            }
	}
        return new Response(json_encode(array('options' => $res)), 200, array('Content-Type'=>'application/json'));
    }
}
