<?php

namespace Modules\ContractForge\Contracts;

interface ContractableEntity
{
    /**
     * Get the entity identifier type (e.g. 'treatment_plan').
     */
    public function getContractEntityType(): string;

    /**
     * Get associative array of tokens and their values.
     */
    public function getContractTokens(): array;

    /**
     * Get a default title for the contract when generated.
     */
    public function getContractTitle(): string;

    /**
     * Get the associated Client ID if any.
     */
    public function getContractClientId(): ?int;
}
