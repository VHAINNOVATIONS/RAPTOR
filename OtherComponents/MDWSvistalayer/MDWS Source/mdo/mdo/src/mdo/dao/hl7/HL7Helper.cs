using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using NHapi.Base.Model;
using gov.va.medora.mdo.domain.ccd;
using NHapi.Model.V24.Datatype;

namespace gov.va.medora.mdo.src.mdo.dao.hl7
{
    public static class HL7Helper
    {

        internal static string getString(AbstractSegment segment, int column, int rep)
        {
            NHapi.Base.Model.IType t = segment.GetField(column, rep);

            if (t is Varies)
            {
                return ((Varies)t).Data.ToString();
            }
            else if (t is NHapi.Model.V24.Datatype.TS)
            {
                return ((NHapi.Model.V24.Datatype.TS)t).ToString();
            }
            else if (t is NM)
            {
                return ((NM)t).Value;
            }
            else
            {
                throw new Exception("Unsupported HL7 type: " + t.TypeName);
            }
        }

    }
}
