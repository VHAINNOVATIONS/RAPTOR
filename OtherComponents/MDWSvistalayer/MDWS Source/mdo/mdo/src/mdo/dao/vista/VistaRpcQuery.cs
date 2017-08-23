using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaRpcQuery
    {
        public String RequestString;
        public String ResponseString;

        public object ParsedResult;

        public DateTime RequestTime;
        public DateTime ResponseTime;
    }
}
