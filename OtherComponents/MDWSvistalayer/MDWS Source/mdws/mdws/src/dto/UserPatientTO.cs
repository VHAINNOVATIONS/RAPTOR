using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class UserPatientTO : AbstractTO
    {
        public UserTO user;
        public PatientTO patient;

        public UserPatientTO() { }

        public UserPatientTO(User user, Patient patient)
        {
            this.user = new UserTO(user);
            this.patient = new PatientTO(patient);
        }

        public UserPatientTO(UserTO userTO, PatientTO patientTO)
        {
            this.user = userTO;
            this.patient = patientTO;
        }
    }
}
