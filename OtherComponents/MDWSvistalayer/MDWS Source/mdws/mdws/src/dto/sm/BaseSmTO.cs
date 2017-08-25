using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class BaseSmTO : AbstractTO
    {
        public int id;
        public int oplock;

        public BaseSmTO() { }
    }
}