using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class ClinicalProcedureTO : AbstractTO
    {
        public SiteTO facility;
        public string name;
        public string timestamp;
        public NoteTO note;
        public string id;
        public string report;

        public ClinicalProcedureTO() { }

        public ClinicalProcedureTO(ClinicalProcedure mdo)
        {
            if (mdo == null)
            {
                return;
            }
            this.name = mdo.Name;
            this.timestamp = mdo.Timestamp;
            this.id = mdo.Id;

            if (mdo.Facility != null)
            {
                this.facility = new SiteTO(mdo.Facility);
            }
            if (mdo.Note != null)
            {
                this.note = new NoteTO(mdo.Note);
            }
            report = mdo.Report;
        }
    }
}