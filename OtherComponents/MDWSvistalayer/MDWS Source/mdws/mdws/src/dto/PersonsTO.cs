using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdws.dto
{
    public class PersonsTO : AbstractTO
    {
        public UserTO user;
        public PatientTO patient;

        public PersonsTO() { }

        public PersonsTO(UserTO user, PatientTO patient)
        {
            this.user = user;
            this.patient = patient;
        }

    }
}
