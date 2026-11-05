<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Applicationform;

/** Résultat explicite d'une tentative de duplication de DAE. */
final class ApplicationformDuplicationResult
{
    public const CATEGORY_REFERENCE = 'reference';
    public const CATEGORY_VALIDATION = 'validation';
    public const CATEGORY_PERSISTENCE = 'persistence';

    /**
     * @param list<array{field: string, label: string, reason: string}> $errors
     */
    private function __construct(
        public readonly bool $success,
        public readonly ?Applicationform $applicationform,
        public readonly ?string $category,
        public readonly string $message,
        public readonly array $errors,
    ) {
    }

    /** Crée un résultat de duplication réussie. */
    public static function success(Applicationform $applicationform): self
    {
        return new self(true, $applicationform, null, '', []);
    }

    /**
     * Crée un résultat de duplication bloquée.
     *
     * @param list<array{field: string, label: string, reason: string}> $errors
     */
    public static function failure(string $category, string $message, array $errors = []): self
    {
        return new self(false, null, $category, $message, $errors);
    }

    /** Indique si la duplication a abouti. */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}
