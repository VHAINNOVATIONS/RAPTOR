using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo
{
    public class PatientCareTeam
    {
        private List<PatientCareTeamMember> members;

        public PatientCareTeam() {
            members = new List<PatientCareTeamMember>();
        }

        public List<PatientCareTeamMember> Members
        {
            get { return members; }
            set { members = value; }
        }
    }
}
