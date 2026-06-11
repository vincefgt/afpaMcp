<?php


namespace App\Builder;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileResponseBuilder
{
    /**
     * @param string $contents
     * @param string $filename
     *
     * @return Response
     */
    public function buildFileResponse(string $contents, string $filename): Response
    {
        // Determine correct Content-Type based on file extension
        $contentType = $this->getContentType($filename);
        
        return new Response(
            $contents,
            200,
            [
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Type' => $contentType,
                'Content-Description' => 'File Transfer',
                'Content-Transfer-Encoding' => 'binary'
            ]
        );
    }
    
    /**
     * Determine the correct Content-Type based on file extension
     *
     * @param string $filename
     * @return string
     */
    private function getContentType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
    
    /**
     * Builds a file response from the given URL and contents.
     *
     * @param string $url The URL of the file to be included in the response.
     * @param object $contents The contents of the file.
     * @return Response The constructed file response.
     */
    public function buildUrlFileResponse(string $url, object $contents): Response
    {

        $fileName = $contents->data->label;
        $contentType = $contents->data->contentType;
        $response = new Response(
            $url,
            200,
            [
                'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT .' ; filename="' . $fileName . '"',
                'Content-Type' => $contentType,
                'Content-Description' => 'URL link',
                'Content-Transfer-Encoding' => 'binary',
                'fileName' => $fileName
            ]
        );

        return $response;
    }
}
