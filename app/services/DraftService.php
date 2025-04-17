<?php

namespace App\Services;

class DraftService extends PostService {
    protected function treatDraftData(array &$data): void 
    {
        parent::treatPostData($data);
        $data["is_draft"] = "1";
    }

    protected function validateDraftData(array $data): void 
    {
        parent::validatePostData($data);
    }

    public function getDrafts(array $columns, array $data)
    {
        $data["p.is_draft"] = "1";
        return parent::getPosts($columns, $data);
    }

    public function getDraftById(string $id, array $columns = ["*"])
    {
        $drafts = $this->getDrafts($columns, [
            "p.id" => $id, 
            "p.is_draft" => "1"
        ]);
        return count($drafts) === 0 ? null : $drafts[0];
    }

    public function updateDraft(string $draft_id, array $updates): array|false 
    {
        $this->treatDraftData($updates);
        $this->validateDraftData($updates);

        $draft = parent::updatePost($draft_id, $updates);
        
        return $draft;
    }
}
