/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements. See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership. The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

namespace php Auth

#
# Enums
#

enum AuthRejectionCode {
  BAD_KEY = 1,
  EXPIRED = 2,
  BAD_GRANT = 3,
  INVALID_SIGNATURE = 4,
  DISABLED_KEY = 5,
  INVALID_LOGIN = 6
}


#
# Exceptions
# (note that internal server errors will raise a TApplicationException, courtesy of Thrift)
#
/** invalid authentication request (invalid keyspace, user does not exist, or credentials invalid) */
exception AuthenticationException {
    1: required string why,
    2: required AuthRejectionCode code
}

/** invalid authorization request (user does not have access to keyspace) */
exception AuthorizationException {
    1: required string why
}
