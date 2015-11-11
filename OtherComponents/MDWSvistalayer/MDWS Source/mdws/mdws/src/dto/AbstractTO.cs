using System;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public abstract class AbstractTO
    {
        public FaultTO fault;
     //   public RpcTO rpc;

        public AbstractTO() { }
    }
}
