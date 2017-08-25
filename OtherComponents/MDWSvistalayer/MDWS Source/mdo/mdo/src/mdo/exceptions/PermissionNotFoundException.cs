using System;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace gov.va.medora.mdo.exceptions
{
    [Serializable]
    public class PermissionNotFoundException : MdoException
    {
        public PermissionNotFoundException() { }
        public PermissionNotFoundException(string message) : base(message) { }
        public PermissionNotFoundException(string message, Exception inner) : base(message, inner) { }
        public PermissionNotFoundException(SerializationInfo info, StreamingContext context) : base(info, context) { }
    }
}
