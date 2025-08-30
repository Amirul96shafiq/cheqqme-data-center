<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentApiResource extends JsonResource
{
    public function toArray($request)
    {
        // Load mentions as full User data
        $mentionsData = [];
        if (! empty($this->mentions)) {
            if (is_array($this->mentions)) {
                $mentionsData = \App\Models\User::whereIn('id', $this->mentions)->get()->toArray();
            } else {
                $m = \App\Models\User::find($this->mentions);
                if ($m) {
                    $mentionsData = [$m->toArray()];
                }
            }
        }

        // Resolve task data (support multiple task IDs)
        $taskFull = [];
        $taskIds = [];
        if (is_array($this->task)) {
            $taskIds = $this->task;
        } elseif ($this->task) {
            $taskIds = [$this->task];
        }
        foreach ($taskIds as $tid) {
            $taskModel = \App\Models\Task::find($tid);
            if (! $taskModel) {
                continue;
            }
            $plain = $taskModel->toArray();
            // Hydrate client (support both scalar id and array of ids)
            if (! empty($plain['client'])) {
                $clientVal = $plain['client'];
                if (is_array($clientVal)) {
                    $plain['client'] = \App\Models\Client::whereIn('id', $clientVal)->get()->toArray();
                } else {
                    $client = \App\Models\Client::find($clientVal);
                    $plain['client'] = $client ? $client->toArray() : null;
                }
            }
            foreach (['project', 'document', 'important_url'] as $key) {
                $val = $plain[$key] ?? null;
                // Normalize to an array of IDs
                $ids = [];
                if (is_array($val)) {
                    $ids = array_values(array_filter($val));
                } elseif ($val !== null) {
                    $ids = [$val];
                }
                if (! empty($ids)) {
                    $modelMap = [
                        'project' => \App\Models\Project::class,
                        'document' => \App\Models\Document::class,
                        'important_url' => \App\Models\ImportantUrl::class,
                    ];
                    $cls = $modelMap[$key];
                    $collection = $cls::whereIn('id', $ids)->get()->toArray();
                    $plain[$key] = $collection;
                } else {
                    $plain[$key] = [];
                }
            }
            $taskFull[] = $plain;
        }
        // Ensure numeric keys start at 0 to render as a JSON array, not an object
        $taskFull = array_values($taskFull);

        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'mentions' => $mentionsData,
            'task' => $taskFull,
            'user' => $this->user ?? null,
            'created_at' => $this->created_at,
        ];
    }
}
