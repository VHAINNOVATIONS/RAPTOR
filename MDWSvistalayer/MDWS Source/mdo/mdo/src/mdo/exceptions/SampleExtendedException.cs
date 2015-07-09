using System;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace gov.va.medora.mdo.exceptions
{
    [Serializable]
    public class SampleExtendedException : MdoException
    {
        public SampleExtendedException() : base() { }
        public SampleExtendedException(string message) : base(message) { }
        public SampleExtendedException(string message, Exception inner) : base(message, inner) { }
        public SampleExtendedException(SerializationInfo info, StreamingContext context) : base(info, context) { }
        public SampleExtendedException(MdoExceptionCode code) : base(code) { }
        public SampleExtendedException(MdoExceptionCode code, Exception inner) : base(code, inner) { }
        public SampleExtendedException(MdoExceptionCode code, string message) : base(code, message) { }
    }

}