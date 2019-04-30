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
 *
 * This Thrift file can be included by other Thrift files that want to share these definitions.
 */

namespace php Debug

#
# Enums
#


#
# Exceptions
# (note that internal server errors will raise a TApplicationException, courtesy of Thrift)
#
/** We have died something out on the server and want to send a structured result to the client for easy debugging. */
exception DieDumpException {
    1: required string json_data
}

exception ServerException {
    1: required i32 code,
    2: required string message,
    3: optional string file,
    4: optional i32 line,
    5: optional list<StackTraceElement> trace
}


#
# Structs
#

// This struct represents a single item from a stack trace.
struct StackTraceElement {
    1: optional string file,
    2: optional i32 line,
    3: optional string function_ref,
    4: optional list<string> arguments,
    5: optional string class_ref,
    6: optional string type,
    7: optional string object
}