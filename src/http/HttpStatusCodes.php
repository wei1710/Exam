<?php

namespace Src\Http;

class HttpStatusCodes
{
  const OK = 200;
  const CREATED = 201;

  const BAD_REQUEST = 400;
  const NOT_FOUND = 404;
  const METHOD_NOT_ALLOWED = 405;
  const CONFLICT = 409;

  const INTERNAL_SERVER_ERROR = 500;
}