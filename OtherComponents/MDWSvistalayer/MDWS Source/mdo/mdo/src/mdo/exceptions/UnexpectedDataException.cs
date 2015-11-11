using System;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace gov.va.medora.mdo.exceptions
{
    [Serializable]
    public class UnexpectedDataException : MdoException
    {
        public UnexpectedDataException() { }
        public UnexpectedDataException(string message) : base(message) { }
        public UnexpectedDataException(string message, Exception inner) : base(message, inner) { }
        public UnexpectedDataException(SerializationInfo info, StreamingContext context) : base(info, context) { }
    }
}
