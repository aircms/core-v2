<?php

declare(strict_types=1);

namespace Air\Type;

class Quote extends TypeAbstract
{
  public string $author = '';
  public string $quote = '';

  public function getAuthor(): string
  {
    return $this->author;
  }

  public function setAuthor(string $author): void
  {
    $this->author = $author;
  }

  public function getQuote(): string
  {
    return $this->quote;
  }

  public function setQuote(string $quote): void
  {
    $this->quote = $quote;
  }
}