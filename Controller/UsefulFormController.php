<?php

namespace Zk2\UsefulBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Zk2\UsefulBundle\Model\AttachAndResizeModelTrait;

class UsefulFormController extends Controller
{
    /**
     * DependentEntity
     *
     * @param Request $request
     * @return Response
     */
    public function dependentEntityAction(Request $request)
    {
        $emName = $request->get('em_name');
        $parentId = $request->get('parent_id');
        $emptyValue = $request->get('empty_value');
        $class = $request->get('class');
        $parentField = $request->get('parent_field');
        $property = $request->get('property');
        $orderProperty = $request->get('order_property');
        $orderDirection = $request->get('order_direction');
        $noResultMsg = $request->get('no_result_msg');

        $response = '';

        if ('' === $parentId) {
            $response = sprintf("<option value=\"\">%s</option>", $noResultMsg);
        } else {
            $em = $this->get('doctrine')->getManager($emName);

            if (!$query = $request->get('query')) {
                $query = sprintf("SELECT e.id, e.%s FROM %s e WHERE e.id != 0 ", $property, $class);
                $rootAlias = 'e.';
            } else {
                $query = urldecode($query);
                preg_match("/^select\s{1,}([A-Za-z0-9_]{1,})\..*$/i", $query, $output);
                $rootAlias = isset($output[1]) ? $output[1].'.' : null;
            }

            $query .= sprintf(
                " AND %s%s=%s ORDER BY %s%s %s",
                $rootAlias,
                $parentField,
                $parentId,
                $rootAlias,
                $orderProperty,
                $orderDirection
            );

            $results = $em->createQuery($query)->getScalarResult();
            if (empty($results)) {
                $response = sprintf("<option value=\"\">%s</option>", $noResultMsg);
            } else {
                if ($emptyValue) {
                    $response .= sprintf("<option value=\"\">%s</option>", $emptyValue);
                }
                foreach ($results as $result) {
                    $response .= sprintf("<option value=\"%d\">%s</option>", $result['id'], $result[$property]);
                }
            }
        }

        return new Response($response);
    }

    /**
     * EntityAjaxAutocomplete
     *
     * @param Request $request
     * @return Response
     */
    public function entityAjaxAutocompleteAction(Request $request)
    {
        $res = [];

        if ($class = $request->get('class') and mb_strlen($request->get('prop'))) {
            $emName = $request->get('em_name');
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager($emName);
            $property = $request->get('property');
            $prop = strtolower($request->get('prop'));
            $conditionOperator = $request->get('condition_operator');
            $maxRows = $request->get('max_rows');

            switch ($conditionOperator) {
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
                    throw new \RuntimeException('Unexpected value of parameter "condition_operator"');
            }

            if (!$query = $request->get('query')) {
                $query = sprintf("SELECT e.id, e.%s FROM %s e WHERE e.id > 0 ", $property, $class);
                $rootAlias = 'e.';
            } else {
                $query = urldecode($query);
                preg_match("/^select\s{1,}([A-Za-z0-9_]{1,})\..*$/i", $query, $output);
                $rootAlias = isset($output[1]) ? $output[1].'.' : null;
            }

            $dbPlatform = $em->getConnection()->getDatabasePlatform()->getName();
            $setType = 'postgresql' === $dbPlatform ? '::text' : null;
            $query .= sprintf(" AND LOWER(%s%s%s) LIKE :like ", $rootAlias, $property, $setType);

            $results = $em->createQuery($query)
                ->setParameter('like', $like)
                ->setMaxResults($maxRows)
                ->getScalarResult();

            foreach ($results as $r) {
                $res[] = ['id' => $r['id'], 'name' => $r[$property]];
            }
        }

        return new JsonResponse(['options' => $res]);
    }

    /**
     * FileAjaxUpload
     *
     * @param Request $request
     * @return Response
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

        $totalClass = $request->request->get('total_class');
        $totalId = $request->request->get('total_id');
        $totalProperty = $request->request->get('total_property');
        $parentClass = $request->request->get('parent_class');
        $parentId = $request->request->get('parent_id');
        $parentProperty = $request->request->get('parent_property');

        if (!class_exists($totalClass)) {
            throw new BadRequestHttpException(sprintf('Object "%s" is not found', $totalClass));
        }

        $em = $this->getDoctrine()->getManager();
        $object = is_numeric($totalId) ? $em->getRepository($totalClass)->find($totalId) : new $totalClass();
        $classUses = class_uses($object);
        if (!isset($classUses[AttachAndResizeModelTrait::class])) {
            throw new BadRequestHttpException(
                sprintf('Object "%s" must be extended AttachAndResizeModelTrait', get_class($object))
            );
        }

        if (!method_exists($object, $totalProperty)) {
            throw new BadRequestHttpException('Method "%s:%s" is not found', $totalClass, $totalProperty);
        }

        if ($parentClass) {
            if (!class_exists($parentClass)) {
                throw new BadRequestHttpException(sprintf('Parent class "%s" not found', $parentClass));
            }
            if (!$parentObject = $em->getRepository($parentClass)->find($parentId)) {
                throw new BadRequestHttpException(sprintf('Parent object "%s" is not found', $parentClass));
            }
            if (!method_exists($parentObject, $parentProperty)) {
                throw new BadRequestHttpException('Method "%s:%s" is not found', $parentClass, $parentProperty);
            }
            $parentObject->$parentProperty($object);
        }

        $mimeType = $request->request->get('mimeType');
        $fileName = $request->request->get('name');
        $tmpFileName = sha1(uniqid(mt_rand(), true));
        file_put_contents(sys_get_temp_dir().DIRECTORY_SEPARATOR.$tmpFileName, base64_decode($src));
        $file = new UploadedFile(
            sys_get_temp_dir().DIRECTORY_SEPARATOR.$tmpFileName,
            $fileName,
            $mimeType
        );
        try {
            $getterTotalProperty = substr_replace($totalProperty, 'get', 0, 3);
            $object->setSourceFile($file);
            $object->uploadImage();
            $em->persist($object);
            $em->flush();
            $response = [
                'status' => 'success',
                'data' => $object->getUploadPath().DIRECTORY_SEPARATOR.$object->$getterTotalProperty(),
            ];
        } catch (\Exception $e) {
            $response = ['status' => 'AttachModelException', 'data' => $e->getMessage()];
        }

        return new JsonResponse($response);
    }
}
