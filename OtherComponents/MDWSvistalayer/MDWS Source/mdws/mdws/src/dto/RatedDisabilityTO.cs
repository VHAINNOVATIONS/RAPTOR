using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class RatedDisabilityTO : AbstractTO
    {
        public string id;
        public string name;
        public string percent;
        public string serviceConnected;
        public string extremityAffected;
        public string originalEffectiveDate;
        public string currentEffectiveDate;

        public RatedDisabilityTO() { }

        public RatedDisabilityTO(RatedDisability mdo)
        {
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.percent = mdo.Percent;
            this.serviceConnected = (mdo.ServiceConnected == true ? "Y" : "N");
            this.extremityAffected = mdo.ExtremityAffected;
            this.originalEffectiveDate = mdo.OriginalEffectiveDate;
            this.currentEffectiveDate = mdo.CurrenEffectiveDate;
        }
    }
}