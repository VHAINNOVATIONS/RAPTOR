using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdws.dto.sm
{
    [Serializable]
    public class SmClinicianTO : BaseSmTO
    {
        public string stationNo;
        public string duz;
        // this determines if the user can create/sign TIU progress notes
        public bool provider;

        //list of distribution groups that the actor belongs 
        //public List<DistributionGroup> DistGroups { get; set; }
        //public ClinicalUserType ClinicalUserType { get; set; }

        public SmClinicianTO() { }

        public SmClinicianTO(mdo.domain.sm.Clinician clinician)
        {
            if (clinician == null)
            {
                return;
            }

            id = clinician.Id;
            stationNo = clinician.StationNo;
            duz = clinician.Duz;
            provider = clinician.Provider;
        }
    }
}
