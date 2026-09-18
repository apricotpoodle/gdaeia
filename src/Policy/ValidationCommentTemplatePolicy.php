<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\ValidationCommentTemplate;
use Authorization\IdentityInterface;

/** Politique du catalogue des commentaires prédéfinis de validation. */
class ValidationCommentTemplatePolicy extends AppPolicy
{
    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\ValidationCommentTemplate $template @return bool */
    public function canView(IdentityInterface $identity, ValidationCommentTemplate $template): bool
    {
        return $this->canIndex($identity, $template);
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\ValidationCommentTemplate $template @return bool */
    public function canIndex(IdentityInterface $identity, ValidationCommentTemplate $template): bool
    {
        return (bool)$this->getValidUser($identity)?->get('issuperuser');
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\ValidationCommentTemplate $template @return bool */
    public function canAdd(IdentityInterface $identity, ValidationCommentTemplate $template): bool
    {
        return $this->canIndex($identity, $template);
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\ValidationCommentTemplate $template @return bool */
    public function canEdit(IdentityInterface $identity, ValidationCommentTemplate $template): bool
    {
        return $this->canIndex($identity, $template);
    }

    /** @param \Authorization\IdentityInterface $identity @param \App\Model\Entity\ValidationCommentTemplate $template @return bool */
    public function canDelete(IdentityInterface $identity, ValidationCommentTemplate $template): bool
    {
        return $this->canIndex($identity, $template);
    }
}
