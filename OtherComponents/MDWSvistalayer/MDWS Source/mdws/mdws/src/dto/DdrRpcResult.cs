using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdws.dto
{
    public class DdrRpcResult : AbstractTO
    {
        public String requestString;
        public String responseString;

        public String[] result;

        public DateTime requestTime;
        public DateTime responseTime;

        public DdrRpcResult() { } 

        public DdrRpcResult(VistaRpcQuery mdo)
        {
            this.requestString = mdo.RequestString;
            this.responseString = mdo.ResponseString;
            this.requestTime = mdo.RequestTime;
            this.responseTime = mdo.ResponseTime;
            this.result = mdo.ParsedResult as String[];
        }
    }
}