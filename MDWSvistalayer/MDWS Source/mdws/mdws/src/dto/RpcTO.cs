using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto
{
    public class RpcTO
    {
        public String Name;
        public String RequestString;
        public String ResponseString;
        public DateTime RequestTime;
        public DateTime ResponseTime;

        public RpcTO() { }

        public RpcTO(gov.va.medora.mdo.RPC rpc)
        {
            this.Name = rpc.Name;
            this.RequestString = rpc.RequestString;
            this.RequestTime = rpc.RequestTime;
            this.ResponseString = rpc.ResponseString;
            this.ResponseTime = rpc.ResponseTime;
        }
    }
}