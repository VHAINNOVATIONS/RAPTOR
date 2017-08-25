using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    /// <summary>
    /// This class should only be used by container object TOs (e.g. PatientMedicalRecordTO) which
    /// hold a number of TO objects and the need for top level error reporting is obvious
    /// </summary>
    public class TaggedFaultArray
    {
        public TaggedFault[] faults;

        public TaggedFaultArray() { }

        public TaggedFaultArray(IndexedHashtable ihs)
        {
            if (ihs == null || ihs.Count == 0)
            {
                return;
            }

            IList<TaggedFault> temp = new List<TaggedFault>();

            for (int i = 0; i < ihs.Count; i++)
            {
                if (MdwsUtils.isException(ihs.GetValue(i)))
                {
                    temp.Add(new TaggedFault((string)ihs.GetKey(i), (Exception)ihs.GetValue(i)));
                }
            }

            if (temp.Count > 0)
            {
                // we want to remove the exceptions from the hashtable because, in container objects, the constructors 
                // for each of the property objects handle exception info. we need to keep the tag though so results collections are consistent
                foreach (TaggedFault tf in temp)
                {
                    ihs.Remove(tf.tag);
                    ihs.Add(tf.tag, null);
                }

                faults = new TaggedFault[temp.Count];
                temp.CopyTo(faults, 0);
            }
        }
    }

    public class TaggedFault
    {
        public string tag;
        public FaultTO fault;

        public TaggedFault() { }

        public TaggedFault(string tag, Exception exc)
        {
            this.tag = tag;
            this.fault = new FaultTO(exc);
        }
    }
}