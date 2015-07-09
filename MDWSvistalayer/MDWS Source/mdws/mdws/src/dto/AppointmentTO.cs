using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class AppointmentTO : AbstractTO
    {
        public string id;
        public string timestamp;
        public string title;
        public string status;
        public string text;
        public TaggedText facility;
        public HospitalLocationTO clinic;
        public string labDateTime;
        public string xrayDateTime;
        public string ekgDateTime;
        public string purpose;
        public string type;
        public string currentStatus;
        public string visitId;
        public string providerName;

        public AppointmentTO() { }

        public AppointmentTO(Appointment mdo)
        {
            this.id = mdo.Id;
            this.timestamp = mdo.Timestamp;
            this.title = mdo.Title;
            this.status = mdo.Status;
            this.text = mdo.Text;
            this.visitId = mdo.VisitId;
            this.providerName = mdo.ProviderName;
            if (mdo.Facility != null)
            {
                this.facility = new TaggedText(mdo.Facility.Id, mdo.Facility.Name);
            }
            if (mdo.Clinic != null)
            {
                this.clinic = new HospitalLocationTO(mdo.Clinic);
                if (mdo.Clinic.Facility != null)
                {
                    this.clinic.facility = new SiteTO();
                    this.clinic.facility.name = mdo.Clinic.Facility.Name;
                }
            }
            this.labDateTime = mdo.LabDateTime;
            this.xrayDateTime = mdo.XrayDateTime;
            this.ekgDateTime = mdo.EkgDateTime;
            this.purpose = mdo.Purpose;
            this.type = mdo.Type;
            this.currentStatus = mdo.CurrentStatus;
        }
    }
}
