<?php

namespace App\Extensions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SessionHandlerInterface;
use MongoDB\Laravel\Query\Builder;
use MongoDB\Laravel\Connection;

class MongoSessionHandler implements SessionHandlerInterface
{
  protected Connection $connection;
  protected Builder $collection;

  public function __construct()
  {
    $this->connection = DB::connection('mongodb');
    $this->collection = $this->connection->collection('sessions');
  }

  public function open(string $path, string $name): bool
  {
    // No specific action needed for opening the session
    return true;
  }

  public function close(): bool
  {
    // No specific action needed for closing the session
    return true;
  }

  public function read(string $id): string
  {
    $session = $this->collection->where('_id', $id)->first();

    return $session ? $session['payload'] : '';
  }

  public function write(string $id, string $data): bool
  {
    $this->collection->updateOrInsert(
      ['_id' => $id],
      ['payload' => $data, 'last_activity' => now()->timestamp]);

    return true;
  }

  public function destroy(string $id): bool
  {
    $this->collection->where('_id', $id)->delete();

    return true;
  }

  public function gc(int $max_lifetime): int
  {
    $expired = now()->subSeconds($max_lifetime);

    $this->collection->where('last_activity', '<', $expired->timestamp)->delete();

    return true;
  }
}
