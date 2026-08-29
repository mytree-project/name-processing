# JSON CLI contract — v1

## Success

Every successful processing operation emits one JSON object:

```json
{
  "schema": "mytree.name-processing.v1",
  "operation": "normalize",
  "input": {},
  "profile": {
    "id": "default",
    "version": "1.0.0"
  },
  "results": [],
  "metadata": {}
}
```

`results` is always an array even when an operation normally returns one value. This avoids changing the contract when an operation can produce multiple candidates later.

## Error

Errors are JSON on STDERR:

```json
{
  "schema": "mytree.name-processing.error.v1",
  "error": {
    "code": "unknown_profile",
    "message": "Unknown profile ..."
  }
}
```

No PHP stack trace is emitted in the public JSON contract.

## Compatibility

Changing field meaning or removing fields requires a new schema ID. Adding optional metadata is permitted within v1.
