using System;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace gov.va.medora.mdo.exceptions
{
    [Serializable]
    public class ConnectionException : MdoException
    {
        public ConnectionException() { }
        public ConnectionException(string message) : base(message) { }
        public ConnectionException(string message, Exception inner) : base(message, inner) { }
        public ConnectionException(SerializationInfo info, StreamingContext context) : base(info, context) { }
        public ConnectionException(MdoExceptionCode code) : base(code) { }
        public ConnectionException(MdoExceptionCode code, Exception inner) : base(code, inner) { }
        public ConnectionException(MdoExceptionCode code, string message) : base(code, message) { }
    }
}
