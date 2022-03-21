<?php

namespace App\Http\Controllers\AdminPanel\Api\v1;

use App\Enums\PostcardStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\PostcardCollection;
use App\Http\Resources\AdminPanel\PostcardResource;
use App\Models\Postcard;
use App\Services\AdminPanel\AdminPanelPostcardService;
use Illuminate\Http\Request;

class PostcardController extends Controller
{
    public function getPostcards(Request $request):PostcardCollection
    {
        $postcardQuery = Postcard::query();

        $postcardQuery->where('draft', '!=', true)
                        ->where('additional_postcard_id', null);

        $postcards = $postcardQuery->paginate(config('admin_panel.postcard_count_paginate'));

        foreach ($postcards as $postcard){
            $adminPanelPostcardService = new AdminPanelPostcardService($postcard);

            $postcard = $adminPanelPostcardService->postcardInfoList();

        }

        return new PostcardCollection($postcards);
    }

    public function getPostcard($id)
    {
        $postcard = Postcard::findOrFail($id);

        $adminPanelPostcardService = new AdminPanelPostcardService($postcard);

        $postcard = $adminPanelPostcardService->postcardInfo();

        return new PostcardResource($postcard);
    }

    public function postcardDelete($id)
    {
        $postcard = Postcard::findOrFail($id);

        $adminPanelPostcardService = new AdminPanelPostcardService($postcard);

        $adminPanelPostcardService->postcardDelete($postcard);

    }


}
