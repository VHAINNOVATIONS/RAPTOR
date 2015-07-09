using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class VisitTO : AbstractTO
    {
        public string id;
        public string type;
        public PatientTO patient;
        public UserTO attending;
        public UserTO provider;
        public string service;
        public HospitalLocationTO location;
        public string patientType;
        public string visitId;
        public string timestamp;
        public string status;
        public SiteTO facility;
 
        public VisitTO() { }

        public VisitTO(Visit mdo)
        {
            if (mdo == null)
            {
                return;
            }
            this.id = mdo.Id;
            this.type = mdo.Type;
            if (mdo.Patient != null)
            {
                this.patient = new PatientTO(mdo.Patient);
            }
            if (mdo.Attending != null)
            {
                this.attending = new UserTO(mdo.Attending);
            }
            if (mdo.Provider != null)
            {
                this.provider = new UserTO(mdo.Provider);
            }
            this.service = mdo.Service;
            if (mdo.Location != null)
            {
                this.location = new HospitalLocationTO(mdo.Location);
            }
            this.patientType = mdo.PatientType;
            this.visitId = mdo.VisitId;
            this.timestamp = mdo.Timestamp;
            this.status = mdo.Status;

            if (mdo.Facility != null)
            {
                facility = new SiteTO(new Site(mdo.Facility.Id, mdo.Facility.Name));
            }
        }
    }
}
