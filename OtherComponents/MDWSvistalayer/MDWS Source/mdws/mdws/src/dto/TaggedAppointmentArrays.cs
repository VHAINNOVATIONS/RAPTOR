using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TaggedAppointmentArrays : AbstractArrayTO
    {
        public TaggedAppointmentArray[] arrays;

        public TaggedAppointmentArrays() { }

        public TaggedAppointmentArrays(IndexedHashtable t)
        {
            if (t == null || t.Count == 0)
            {
                return;
            }
            arrays = new TaggedAppointmentArray[t.Count];
            for (int i = 0; i < t.Count; i++)
            {
                if (t.GetValue(i) == null)
                {
                    arrays[i] = new TaggedAppointmentArray((string)t.GetKey(i));
                }
                else if (MdwsUtils.isException(t.GetValue(i)))
                {
                    arrays[i] = new TaggedAppointmentArray((string)t.GetKey(i), (Exception)t.GetValue(i));
                }
                else if (t.GetValue(i).GetType() == typeof(System.Collections.Hashtable))
                {
                    IList<Appointment> temp = ((System.Collections.Hashtable)t.GetValue(i))["appointments"] as IList<Appointment>;
                    if (temp == null || temp.Count == 0)
                    {
                        arrays[i] = new TaggedAppointmentArray((string)t.GetKey(i));
                    }
                    else
                    {
                        Appointment[] ary = new Appointment[temp.Count];
                        temp.CopyTo(ary, 0);
                        arrays[i] = new TaggedAppointmentArray((string)t.GetKey(i), ary);
                    }

                }
                else if (t.GetValue(i).GetType().IsArray)
                {
                    arrays[i] = new TaggedAppointmentArray((string)t.GetKey(i), (Appointment[])t.GetValue(i));
                }
                else
                {
                    arrays[i] = new TaggedAppointmentArray((string)t.GetKey(i), (Appointment)t.GetValue(i));
                }
            }
            count = t.Count;
        }

        internal void add(string siteId, IList<Appointment> appts)
        {
            if (arrays == null || arrays.Length == 0)
            {
                arrays = new TaggedAppointmentArray[1];
            }
            else
            {
                Array.Resize<TaggedAppointmentArray>(ref arrays, arrays.Length + 1);
            }

            arrays[arrays.Length - 1] = new TaggedAppointmentArray(siteId, ((List<Appointment>)appts).ToArray());
        }
    }
}
