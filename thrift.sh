#!/usr/bin/env bash

thrift --gen php:server,oop,json,validate,nsglobal=Steenbag\\Tubes\\General -v -strict --out src/ src/thrift/main.thrift
thrift --gen php:server,oop,json,validate,nsglobal=Steenbag\\Tubes\\General -v -strict --out src/ src/thrift/auth.thrift
thrift --gen php:server,oop,json,validate,nsglobal=Steenbag\\Tubes\\General -v -strict --out src/ src/thrift/debug.thrift