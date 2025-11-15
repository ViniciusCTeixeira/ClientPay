<?php

class Paginator
{
    public int $page;
    public int $per;
    public int $total;

    public function __construct(int $page, int $per, int $total)
    {
        $this->page = $page;
        $this->per = $per;
        $this->total = $total;
    }

    public function pages(): int
    {
        return (int)ceil($this->total / $this->per);
    }
}
