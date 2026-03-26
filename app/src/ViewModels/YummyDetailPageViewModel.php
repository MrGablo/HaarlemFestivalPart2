<?php

namespace App\ViewModels;

use App\Models\YummyEvent;

class YummyDetailPageViewModel
{
    public YummyEvent $event;
    public array $sessions;
    public array $pageContent;
    public string $pageTitle;

    public function __construct(YummyEvent $event, array $sessions, array $pageContent)
    {
        $this->event = $event;
        $this->sessions = $sessions;
        $this->pageTitle = "Yummy Event - " . $event->title;

        $this->pageContent = [
            'gallery' => array_map(function($img) {
                return [
                    'src' => $img['path'] ?? '',
                    'alt' => $img['caption'] ?? '',
                    'caption' => $img['caption'] ?? ''
                ];
            }, $pageContent['heroSection']['images'] ?? []),
            'aboutSection' => $pageContent['aboutSection']['descriptionHtml'] ?? '',
            'amuse_bouche' => [
                'html' => $pageContent['contentSection1']['html'] ?? '',
                'images' => array_column($pageContent['contentSection1']['images'] ?? [], 'path'),
                'caption' => $pageContent['contentSection1']['images'][0]['caption'] ?? ''
            ],
            'chef' => [
                'html' => $pageContent['chefSection']['html'] ?? '',
                'image' => $pageContent['chefSection']['imagePath'] ?? '',
            ],
            'menu' => [
                'html' => $pageContent['menuSection']['html'] ?? '',
                'image' => $pageContent['menuSection']['imagePath'] ?? '',
            ],
            'informationBlock' => $pageContent['informationBlock'] ?? '',
        ];
    }
}
