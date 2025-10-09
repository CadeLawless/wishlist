<?php

namespace App\Core;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $guarded = [];
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find($id): ?static
    {
        $data = $this->db->table($this->table)
            ->where($this->primaryKey, $id)
            ->first();

        if (!$data) {
            return null;
        }

        return $this->fill($data);
    }

    public function where(string $column, $operator, $value = null): Database
    {
        return $this->db->table($this->table)->where($column, $operator, $value);
    }

    public function all(): array
    {
        $data = $this->db->table($this->table)->get();
        return array_map([$this, 'fill'], $data);
    }

    public function create(array $data): static
    {
        $data = $this->filterFillable($data);
        $this->db->table($this->table)->insert($data);
        
        $id = $this->db->lastInsertId();
        return $this->find($id);
    }

    public function update(array $data): bool
    {
        $data = $this->filterFillable($data);
        return $this->db->table($this->table)
            ->where($this->primaryKey, $this->{$this->primaryKey})
            ->update($data);
    }

    public function delete(): bool
    {
        return $this->db->table($this->table)
            ->where($this->primaryKey, $this->{$this->primaryKey})
            ->delete();
    }

    public function save(): bool
    {
        if (isset($this->{$this->primaryKey})) {
            return $this->update($this->toArray());
        } else {
            $data = $this->toArray();
            unset($data[$this->primaryKey]);
            $this->db->table($this->table)->insert($data);
            $this->{$this->primaryKey} = $this->db->lastInsertId();
            return true;
        }
    }

    public function fill(array $data): static
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    public function toArray(): array
    {
        $data = [];
        foreach ($this->fillable as $field) {
            if (isset($this->$field)) {
                $data[$field] = $this->$field;
            }
        }
        return $data;
    }

    protected function filterFillable(array $data): array
    {
        if (!empty($this->fillable)) {
            return array_intersect_key($data, array_flip($this->fillable));
        }
        
        if (!empty($this->guarded)) {
            return array_diff_key($data, array_flip($this->guarded));
        }
        
        return $data;
    }

    public function __get(string $name)
    {
        return $this->$name ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->$name = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->$name);
    }

    // Relationship methods
    public function hasMany(string $model, string $foreignKey, string $localKey = null): array
    {
        $localKey = $localKey ?? $this->primaryKey;
        $relatedModel = new $model();
        
        return $relatedModel->where($foreignKey, $this->$localKey)->get();
    }

    public function belongsTo(string $model, string $foreignKey, string $localKey = null): ?static
    {
        $localKey = $localKey ?? $this->primaryKey;
        $relatedModel = new $model();
        
        return $relatedModel->find($this->$foreignKey);
    }
}
