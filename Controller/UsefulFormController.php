<?php

namespace Zk2\UsefulBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zk2\UsefulBundle\Model\AttachModelException;

class UsefulFormController extends Controller
{
    /**
     * DependentEntity
     */
    public function dependentEntityAction(Request $request)
    {
        $translator = $this->get('translator');

        $em_name = $request->get('em_name');
        $em = $this->get('doctrine')->getManager($em_name);

        $parent_id = $request->get('parent_id');
        $empty_value = $request->get('empty_value');
        $class = $request->get('class');
        $parent_field = $request->get('parent_field');
        $property = $request->get('property');
        $order_property = $request->get('order_property');
        $order_direction = $request->get('order_direction');
        $no_result_msg = $request->get('no_result_msg');

        if (!$query = $request->get('query')) {
            $query = sprintf(
                "SELECT e.id,e.%s FROM %s e WHERE e.id<>0 ",
                $property,
                $class
            );
            $rootAlias = 'e';
        } else {
            $query = urldecode($query);
            $rootAlias = substr($query, 7, 1);
        }

        $query .= sprintf(
            " AND %s.%s='%s' ORDER BY %s.%s %s",
            $rootAlias,
            $parent_field,
            $parent_id,
            $rootAlias,
            $order_property,
            $order_direction
        );

        $results = $em->createQuery($query)
            ->getScalarResult();
        $html = '';

        if (empty($results)) {
            return new Response('<option value="">'.$translator->trans($no_result_msg).'</option>');
        }

        if ($empty_value) {
            $html .= '<option value="">'.$translator->trans($empty_value).'</option>';
        }

        foreach ($results as $result) {
            $html .= sprintf("<option value=\"%d\">%s</option>", $result['id'], $result[$property]);
        }

        return new Response($html);
    }

    /**
     * EntityAjaxAutocomplete
     */
    public function entityAjaxAutocompleteAction(Request $request)
    {
        $em_name = $request->get('em_name');
        $em = $this->get('doctrine')->getManager($em_name);
        $res = array();

        if ($class = $request->get('class')) {
            $property = $request->get('property');
            $prop = $request->get('prop');
            $condition_operator = $request->get('condition_operator');
            $max_rows = $request->get('max_rows');

            switch ($condition_operator) {
                case "begins_with":
                    $like = $prop.'%';
                    break;
                case "ends_with":
                    $like = '%'.$prop;
                    break;
                case "contains":
                    $like = '%'.$prop.'%';
                    break;
                default:
                    throw new \Exception('Unexpected value of parameter "condition_operator"');
            }

            if (!$query = $request->get('query')) {
                $query = sprintf("SELECT e.id,e.%s FROM %s e WHERE e.id>0 ", $property, $class);
                $rootAlias = 'e';
            } else {
                $query = urldecode($query);
                $rootAlias = substr($query, 7, 1);
            }

            $query .= sprintf(" AND LOWER(%s.%s) LIKE LOWER(:like) ", $rootAlias, $property);

            $results = $em->createQuery($query)
                ->setParameter('like', $like)
                ->setMaxResults($max_rows)
                ->getScalarResult();

            foreach ($results as $r) {
                $res[] = array('id' => $r['id'], 'name' => $r[$property]);
            }
        }

        return new Response(json_encode(array('options' => $res)), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * EntityAjaxAutocomplete
     */
    public function fileAjaxUploadAction(Request $request)
    {
        if (!$src = $request->request->get('src')) {
            return new Response();
        }
        $src = explode(';base64,', $src);
        if (isset($src[0])) {
            unset($src[0]);
            $src = implode('', $src);
        }
        $response = array('status' => 'not_saved', 'data' => '');
        $total_class = $request->request->get('total_class');
        $total_id = $request->request->get('total_id');
        $total_method = $request->request->get('total_method');
        $parent_class = $request->request->get('parent_class');
        $parent_id = $request->request->get('parent_id');
        $parent_method = $request->request->get('parent_method');
        if (class_exists($total_class)) {
            $em = $this->getDoctrine()->getManager();
            $object = is_numeric($total_id) ? $em->getRepository($total_class)->find($total_id) : new $total_class();
            if ($parent_class and class_exists($parent_class) and $parent_id and $parent_method) {
                if (!$parent_object = $em->getRepository($parent_class)->find($parent_id) or !method_exists(
                        $parent_object,
                        $parent_method
                    )
                ) {
                    return new Response(
                        json_encode(array('status' => 'error', 'data' => 'Error download')),
                        200,
                        array('Content-Type' => 'application/json')
                    );
                }
                $parent_object->$parent_method($object);
            }
            $mimeType = $request->request->get('mimeType');
            $file_name = $request->request->get('name');
            $tmp_file_name = sha1(uniqid(mt_rand(), true));
            file_put_contents('/tmp/'.$tmp_file_name, base64_decode($src));
            $file = new UploadedFile(
                '/tmp/'.$tmp_file_name,
                $file_name,
                $mimeType
            );
            if (method_exists($object, $total_method)) {
                $object->setTotalFile($file);
                $object->$total_method($file_name);
                $get_total_method = str_replace('set', 'get', $total_method);
                try {
                    $em->persist($object);
                    $em->flush();
                    $response = array('status' => 'success', 'data' => $object->$get_total_method());
                } catch (AttachModelException $e) {
                    $response = array('status' => 'error_size', 'data' => $e->getMessage());
                } catch (\Exception $e) {
                    $response = array('status' => 'error', 'data' => 'Error download');
                }
            }
        }

        return new Response(json_encode($response), 200, array('Content-Type' => 'application/json'));
    }
}
