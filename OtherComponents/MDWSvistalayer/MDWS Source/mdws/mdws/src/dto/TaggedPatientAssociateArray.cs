using System;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedPatientAssociateArray : AbstractTaggedArrayTO
    {
        public PatientAssociateTO[] pas;

        public TaggedPatientAssociateArray() { }

        public TaggedPatientAssociateArray(string tag)
        {
            this.tag = tag;
            this.count = 0;
        }

        public TaggedPatientAssociateArray(string tag, PatientAssociate[] mdos)
        {
            this.tag = tag;
            if (mdos == null)
            {
                this.count = 0;
                return;
            }
            this.pas = new PatientAssociateTO[mdos.Length];
            for (int i = 0; i < mdos.Length; i++)
            {
                this.pas[i] = new PatientAssociateTO(mdos[i]);
            }
            this.count = pas.Length;
        }

        public TaggedPatientAssociateArray(string tag, PatientAssociate mdo)
        {
            this.tag = tag;
            if (mdo == null)
            {
                this.count = 0;
                return;
            }
            this.pas = new PatientAssociateTO[1];
            this.pas[0] = new PatientAssociateTO(mdo);
            this.count = 1;
        }

        public TaggedPatientAssociateArray(string tag, Exception e)
        {
            this.tag = tag;
            this.fault = new FaultTO(e);
        }
    }
}
