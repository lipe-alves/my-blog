<?php

namespace App\Services;

class DraftService extends PostService {
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

    protected function treatDraftData(array &$data): void 
    {
        parent::treatPostData($data);
        $data["is_draft"] = "1";
    }

    protected function validateDraftData(array $data): void 
    {
        parent::validatePostData($data);
    }

    public function updateDraft(string $draft_id, array $updates): array|false 
    {
        unset($updates["post_id"]);

        $this->treatDraftData($updates);
        $this->validateDraftData($updates);

        $draft = parent::updatePost($draft_id, $updates);
        
        return $draft;
    }

    public function createDraft(array $data): array|false
    {
        $data = array_merge([
            "title"      => "",
            "text"       => "",
            "categories" => []
        ], $data);

        $this->treatDraftData($data);
        $this->validateDraftData($data);
        
        $success = $this->insert("Post", [$data]);
        if (!$success) return false;

        $draft_id = $success;
        $draft = $this->getDraftById($draft_id);
        
        return $draft;
    }
}
