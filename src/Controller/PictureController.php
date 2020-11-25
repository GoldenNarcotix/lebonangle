<?php


namespace App\Controller;


use App\Entity\Picture;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PictureController extends AbstractController
{
    public function __invoke(Request $request): Picture
    {
        $uploadedFile = $request->files->get('file');
        if(!$uploadedFile)
        {
            throw new BadRequestHttpException('"file" is required');
        }

        $picture = new Picture();
        $picture->setFile($uploadedFile);

        return $picture;
    }

}