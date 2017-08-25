using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientLocationTO : AbstractTO
    {
        public SiteTO medicalCenter;
        public HospitalLocationTO inpatientLocation;
        public string deceasedDate;
        public TaggedAppointmentArrays futureAppointments;
        public PatientAssociateArray contacts;

        public PatientLocationTO() { }

    }
}
