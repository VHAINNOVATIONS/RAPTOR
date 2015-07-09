using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class ClaimTO : AbstractTO
    {
        public string id;
        public string patientId;
        public string patientName;
        public string patientSsn;
        public string episodeDate;
        public string timestamp;
        public string lastEditTimestamp;
        public string insuranceName;
        public string cost;
        public string billableStatus;
        public string condition;
        public string serviceConnectedPercent;
        public string consultId;
        public string comment;

        public ClaimTO() { }

        public ClaimTO(Claim mdo)
        {
            this.id = mdo.Id;
            this.patientId = mdo.PatientId;
            this.patientName = mdo.PatientName;
            this.patientSsn = mdo.PatientSSN;
            this.episodeDate = mdo.EpisodeDate;
            this.timestamp = mdo.Timestamp;
            this.lastEditTimestamp = mdo.LastEditTimestamp;
            this.insuranceName = mdo.InsuranceName;
            this.cost = mdo.Cost;
            this.billableStatus = mdo.BillableStatus;
            this.condition = mdo.Condition;
            this.serviceConnectedPercent = mdo.ServiceConnectedPercent;
            this.consultId = mdo.ConsultId;
            this.comment = mdo.Comment;
        }
    }
}