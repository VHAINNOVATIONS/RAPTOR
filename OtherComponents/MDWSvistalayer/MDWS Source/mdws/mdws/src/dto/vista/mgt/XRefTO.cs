using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdws.dto.vista.mgt
{
    [Serializable]
    public class XRefTO : AbstractTO
    {
        public string dd;
        public string fieldName;
        public string fieldNumber;
        public VistaFileTO file;
        public string name;

        public XRefTO() { }

        public XRefTO(CrossRef mdo)
        {
            if (mdo == null)
            {
                return;
            }

            this.dd = mdo.DD;
            this.fieldName = mdo.FieldName;
            this.fieldNumber = mdo.FieldNumber;
            if (mdo.File != null)
            {
                this.file = new VistaFileTO(mdo.File);
            }
            this.name = mdo.Name;
        }
    }
}