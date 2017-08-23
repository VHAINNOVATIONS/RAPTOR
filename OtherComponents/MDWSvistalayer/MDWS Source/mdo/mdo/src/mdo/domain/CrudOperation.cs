using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class CrudOperation
    {
        public CrudOperationType Type;
        public RPC RPC;
        public object Result;

        public CrudOperation() { }
    }

    public enum CrudOperationType
    {
        CREATE, 
        READ,
        UPDATE,
        DELETE
    }
}
