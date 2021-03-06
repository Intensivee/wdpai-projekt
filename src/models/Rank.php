<?php


class Rank
{
    private int $id;
    private string $rank;

    public function __construct(int $id, string $rank)
    {
        $this->id = $id;
        $this->rank = $rank;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getRank(): ?string
    {
        return $this->rank;
    }

    public function setRank(string $rank): void
    {
        $this->rank = $rank;
    }
}