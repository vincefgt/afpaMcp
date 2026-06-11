<?php

declare(strict_types=1);

namespace App\Controller;

use App\Builder\ApiGatewayHeadersBuilder;
use App\Builder\FileResponseBuilder;
use App\Client\FileSystemClient;
use App\Client\UploadClient;
use App\Entity\ApiGatewayHeaders;
use App\Manager\ApiGatewayClientManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Exception\MissingResourceException;
use App\Manager\RequestConfigurationManager;
use App\Builder\JsonErrorResponseBuilder;
use App\Handler\RequestKeysHandler;
use App\Manager\JwtManager;
use Psr\Log\LoggerInterface;

class FileController extends AbstractController
{


    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Request                 $request
     * @param UploadClient            $uploadClient
     * @param ApiGatewayClientManager $clientManager
     *
     * @return JsonResponse
     */
    public function upload(
        Request $request,
        UploadClient $uploadClient,
        ApiGatewayClientManager $clientManager,
        JwtManager $jwtManager,
        ApiGatewayHeadersBuilder $headersBuilder
    ): JsonResponse {
        
        $headers = $headersBuilder->createFromRequest($request, $jwtManager);

        //We get the value of the primarykey from the object where we upload the file for ERP to find after upload
        $idFile = null;
        if (empty($request->attributes->get('idFile'))) {
            $dataKeyName = $request->attributes->get(RequestKeysHandler::DATA_KEY_NAME_FIELD);
            $primaryKeyName = $request->attributes->get(RequestKeysHandler::PRIMARY_KEY_NAME_FIELD);
            $data = $request->request->all();
            $data = json_decode($data[$dataKeyName], true);
            $idFile = $data[$primaryKeyName];
            $idFile = intval($idFile);
        } else {
            $idFile = $request->attributes->get('idFile');
        }

        if (!empty($request->files->get('file'))) { //Send file if not null
            $request->request->add([ 'idFile' => $idFile ]);
            if (is_array($request->files->get('file'))) {
                $id = 0;
                foreach ($request->files->get('file') as $file) {
                    $request->request->add(
                        [
                            'file_' . $id => [$file->getClientOriginalName(),
                            file_get_contents($file->getRealPath())]
                        ]
                    );
                    $id++;
                }
            } else {
                $request->request->add(
                    [
                        'file' => [$request->files->get('file')->getClientOriginalName(),
                        file_get_contents($request->files->get('file')->getRealPath())]
                    ]
                );
            }
        }

        $upload = $uploadClient->call($headers, $request->request->all());
        if ($upload->getStatusCode() != Response::HTTP_OK) {
            $uploadBody = $upload->getBody()->getContents();
            $uploadArray = json_decode($uploadBody, true);

            return new JsonResponse($uploadArray, $upload->getStatusCode());
        }

        return $clientManager->sendToApiGateway($request, $clientManager);
    }

    /**
     * @param Request                  $request
     * @param FileSystemClient         $fileClient
     * @param FileResponseBuilder      $fileResponseBuilder
     * @param ApiGatewayClientManager  $clientManager
     * @param ApiGatewayHeadersBuilder $headersBuilder
     *
     * @return JsonResponse|Response
     */
    public function download(
        Request $request,
        FileSystemClient $fileClient,
        FileResponseBuilder $fileResponseBuilder,
        ApiGatewayClientManager $clientManager,
        ApiGatewayHeadersBuilder $headersBuilder,
        JwtManager $jwtManager
    ) {
        $result = $clientManager->sendToApiGateway($request, $clientManager);

        if ($result->getStatusCode() == Response::HTTP_OK) {
            $contents = json_decode($result->getContent());

            if ($contents->data && isset($contents->data->fid)) {
                try {
                    $file = $fileClient->download(
                        $contents->data->fid,
                        $headersBuilder->createFromRequest($request, $jwtManager)
                    );
                } catch (MissingResourceException $exception) {
                    return new JsonResponse(['error' => $exception->getMessage()], 404);
                }
                if ($file !== false) {
                    return $fileResponseBuilder->buildFileResponse($file, $contents->data->label);
                }
            }

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return $result;
    }

    /**
     * @param Request                  $request
     * @param FileSystemClient         $fileClient
     * @param FileResponseBuilder      $fileResponseBuilder
     * @param ApiGatewayClientManager  $clientManager
     * @param ApiGatewayHeadersBuilder $headersBuilder
     *
     * @return JsonResponse|Response
     */
    public function downloadUrl(
        Request $request,
        FileSystemClient $fileClient,
        FileResponseBuilder $fileResponseBuilder,
        ApiGatewayClientManager $clientManager,
        ApiGatewayHeadersBuilder $headersBuilder,
        JwtManager $jwtManager
    ) {
        $result = $clientManager->sendToApiGateway($request, $clientManager);

        if ($result->getStatusCode() == Response::HTTP_OK) {
            $contents = json_decode($result->getContent());
            if ($contents->data && isset($contents->data->fid)) {
                $url = $fileClient->downloadLink(
                    $contents->data->fid,
                    $headersBuilder->createFromRequest($request, $jwtManager));
                return $fileResponseBuilder->buildUrlFileResponse($url,$contents);
            }
        }

        return $result;
    }
}
