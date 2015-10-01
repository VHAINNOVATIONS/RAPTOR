using System;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace gov.va.medora.mdo.exceptions
{
    [Serializable]
    public class PermissionLockedException : MdoException
    {
        public PermissionLockedException() { }
        public PermissionLockedException(string message) : base(message) { }
        public PermissionLockedException(string message, Exception inner) : base(message, inner) { }
        public PermissionLockedException(SerializationInfo info, StreamingContext context) : base(info, context) { }
    }
}
