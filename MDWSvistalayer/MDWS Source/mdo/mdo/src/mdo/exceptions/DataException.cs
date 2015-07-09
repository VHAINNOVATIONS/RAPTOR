using System;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace gov.va.medora.mdo.exceptions
{
    [Serializable]
    public class DataException : MdoException
    {
        public DataException() { }
        public DataException(string message) : base(message) { }
        public DataException(string message, Exception inner) : base(message, inner) { }
        public DataException(SerializationInfo info, StreamingContext context) : base(info, context) { }
    }
}
