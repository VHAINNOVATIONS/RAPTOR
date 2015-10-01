using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class TimeSlotArray : AbstractTaggedArrayTO
    {
        public TimeSlotTO[] slots;

        public TimeSlotArray() { }

        public TimeSlotArray(IList<TimeSlot> mdos)
        {
            if (mdos == null || mdos.Count == 0)
            {
                return;
            }

            this.count = mdos.Count;
            this.slots = new TimeSlotTO[mdos.Count];
            for (int i = 0; i < mdos.Count; i++)
            {
                this.slots[i] = new TimeSlotTO(mdos[i]);
            }
        }
    }
}