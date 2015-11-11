using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdws.dto.vista.mgt
{
    [Serializable]
    public class VistaFileTO : AbstractTO
    {
        public VistaFieldTO[] fields;
        public string global;
        public string lastIenAssigned;
        public string name;
        public string number;
        public string numberOfRecords;
        public XRefArray xrefs;

        public VistaFileTO() { }

        public VistaFileTO(VistaFile mdo)
        {
            if (mdo == null)
            {
                return;
            }

            if (mdo.FieldsDict != null && mdo.FieldsDict.Count > 0)
            {
                this.fields = new VistaFieldTO[mdo.FieldsDict.Count];
                IList<VistaFieldTO> fieldList = new List<VistaFieldTO>();
                foreach (VistaField field in mdo.FieldsDict.Values)
                {
                    fieldList.Add(new VistaFieldTO(field));
                }
                fieldList.CopyTo(this.fields, 0);
            }
            this.global = mdo.Global;
            this.lastIenAssigned = mdo.LastIenAssigned;
            this.name = mdo.FileName;
            this.number = mdo.FileNumber;
            this.numberOfRecords = mdo.NumberOfRecords;
            if (mdo.XRefs != null && mdo.XRefs.Count > 0)
            {
                this.xrefs = new XRefArray(mdo.XRefs);
            }
        }
    }
}