using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientCareTeamTO : AbstractTO
    {
        private List<PatientCareTeamMemberTO> members = new List<PatientCareTeamMemberTO>();

        public PatientCareTeamTO() { }

        public PatientCareTeamTO(PatientCareTeam patientCareTeam)
        {
            if (patientCareTeam == null)
                return;

            //this.patient = new PatientTO(patientCareTeam.Patient);
            foreach(PatientCareTeamMember member in patientCareTeam.Members){

                this.members.Add(new PatientCareTeamMemberTO(member));
            }
        }

        public List<PatientCareTeamMemberTO> Members
        {
            get { return members; }
            set { members = value; }
        }
    }
}