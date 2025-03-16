# Object Resources

Main-Level
```json
{
    "message": "string",
    "errors": "array of object issue",
    "meta": "object meta data"
}
```

Object Issue
```json
{
    "message": "string",
    "attribute": "string",
    "prefix": "string",
    "violated": "string"
}
```

Object Meta Data
```json
{
    "timestamp": "string",
    "trace_id": "string|integer",
    "exception": "string|missing",
    "most_relevant_trace": "object stack trace|missing",
    "stacktrace": "array of object stack trace"
}
```

Object Stack Trace
```json
{
    "file": "string",
    "line": "integer",
    "function": "string",
    "in_app": "boolean"
}
```

## Example Response

Full Response
```json
{
    "message": "Internal Server Error",
    "errors": [
        {
            "message": "An unexpected error occurred.",
            "attribute": "internal",
            "prefix": "internal",
            "violated": "system"
        }
    ],
    "meta": {
        "timestamp": "2025-03-12T16:10:00Z",
        "trace_id": "c3d4e5f6",
        "exception": "RuntimeException",
        "most_relevant_trace": {
            "file": "/path/to/file",
            "line": 30,
            "function": "defectedFunction",
            "in_app": true
        },
        "stack_trace": [
            {
                "file": "/path/to/file",
                "line": 30,
                "function": "defectedFunction",
                "in_app": true
            },
            {
                "file": "/path/to/file",
                "line": 30,
                "function": "someDefectedFunction",
                "in_app": false
            }
        ]
    }
}
```

Simple Response
```json
{
    "message": "Bad Request",
    "errors": [
        {
            "message": "An unexpected error occurred.",
            "attribute": "internal",
            "prefix": "internal",
            "violated": "request"
        }
    ],
    "meta": {
        "timestamp": "2025-03-12T16:10:00Z",
        "trace_id": "c3d4e5f6"
    }
}
```