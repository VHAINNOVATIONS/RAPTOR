using System;
using System.Collections;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class PatientAssociateArray : AbstractArrayTO
    {
        public PatientAssociateTO[] pas;

        public PatientAssociateArray() { }

        public PatientAssociateArray(PatientAssociate[] mdo)
        {
            setProps(mdo);
        }

        public PatientAssociateArray(ArrayList lst)
        {
            setProps((PatientAssociate[])lst.ToArray(typeof(PatientAssociate)));    
        }

        private void setProps(PatientAssociate[] mdo)
        {
            if (mdo == null)
            {
                return;
            }
            pas = new PatientAssociateTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                pas[i] = new PatientAssociateTO(mdo[i]);
            }
            count = mdo.Length;
        }

        public PatientAssociateArray(SortedList lst)
        {
            if (lst == null || lst.Count == 0)
            {
                count = 0;
                return;
            }
            pas = new PatientAssociateTO[lst.Count];
            IDictionaryEnumerator e = lst.GetEnumerator();
            int i = 0;
            while (e.MoveNext())
            {
                pas[i++] = new PatientAssociateTO((PatientAssociate)e.Value);
            }
            count = lst.Count;
        }
    }
}
