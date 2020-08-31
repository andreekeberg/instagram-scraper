# Instagram

| Name | Description |
|------|-------------|
|[getUser](#instagramgetuser)|Get public users media|
|[getTag](#instagramgettag)|Get public media with specified tag|

## Instagram::getName  

**Description**

```php
public getUser (string $user, int|null $limit)
```

Get public users media, with an optional limit.

Returns false on errors, or when no items are found.

**Parameters**

* `(string) $user`
* `(int|null) $limit`

**Return Values**

`array|false`

<hr />

## Instagram::getName  

**Description**

```php
public getTag (string $tag, int|null $limit)
```

Get public media with specified tag, with an optional limit.

Returns false on errors, or when no items are found.

**Parameters**

* `(string) $tag`
* `(int|null) $limit`

**Return Values**

`array|false`
