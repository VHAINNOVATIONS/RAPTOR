using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm.enums;

namespace gov.va.medora.mdo.domain.sm
{
    public class Clinician : gov.va.medora.mdo.domain.sm.User
    {
        public string StationNo { get; set; }
        public string Duz { get; set; }
        //list of distribution groups that the actor belongs 
        public List<DistributionGroup> DistGroups { get; set; }
        // this determines if the user can create/sign TIU progress notes
        public bool Provider { get; set; }

        public ClinicalUserType ClinicalUserType { get; set; }

        public Clinician()
        {
            //UserType = UserTypeEnum.CLINICIAN;
            //ParticipantType = ParticipantTypeEnum.CLINICIAN;
        }

    }
}
