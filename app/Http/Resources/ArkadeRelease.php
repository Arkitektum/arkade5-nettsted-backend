<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArkadeRelease extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'brukergrensesnitt' => $this->user_interface,
            'versjonsnummer' => $this->version_number,
            'utgivelsesdato' => $this->released_at->format("d.m.Y"),
            'antall_nedlastinger' => $this->downloads->count(),
            'links' => [
                'self' => route('release', $this->id),
                'parent' => route('releases'),
                'nedlastinger' => route('downloads', ['utgivelse' => $this->id]),
            ],
        ];
    }
}
