using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedPatientArray : AbstractTaggedArrayTO
    {
        public PatientTO[] patients;

        public TaggedPatientArray() { }

        public TaggedPatientArray(string tag, IList<Patient> patients)
        {
            this.tag = tag;
            if (patients == null || patients.Count == 0)
            {
                this.count = 0;
                return;
            }
            this.patients = new PatientTO[patients.Count];
            for (int i = 0; i < patients.Count; i++)
            {
                this.patients[i] = new PatientTO(patients[i]);
            }
            this.count = patients.Count;
        }

        public TaggedPatientArray(string tag, Patient[] patients)
        {
            this.tag = tag;
            if (patients == null)
            {
                this.count = 0;
                return;
            }
            this.patients = new PatientTO[patients.Length];
            for (int i = 0; i < patients.Length; i++)
            {
                this.patients[i] = new PatientTO(patients[i]);
            }
            this.count = patients.Length;
        }

        public TaggedPatientArray(string tag, Patient patient)
        {
            this.tag = tag;
            if (patient == null)
            {
                this.count = 0;
                return;
            }
            this.patients = new PatientTO[1];
            this.patients[0] = new PatientTO(patient);
            this.count = 1;
        }

        public TaggedPatientArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedPatientArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
