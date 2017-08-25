using System;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientRecordFlagArray : AbstractArrayTO
    { 
        public PatientRecordFlagTO[] flags;

        public PatientRecordFlagArray() { }

        public PatientRecordFlagArray(PatientRecordFlag[] mdos)
        {
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.flags = new PatientRecordFlagTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.flags[i] = new PatientRecordFlagTO(mdos[i]);
            }
            this.count = flags.Length;
        }

        public PatientRecordFlagArray(PatientRecordFlag mdo)
        {
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.flags = new PatientRecordFlagTO[1];
            this.flags[0] = new PatientRecordFlagTO(mdo);
            this.count = 1;
        }

        public PatientRecordFlagArray(Exception e)
        {
            this.fault = new FaultTO(e);
        }
    }
}
