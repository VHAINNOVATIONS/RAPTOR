using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class AppointmentTypeArray : AbstractArrayTO
    {
        public AppointmentTypeTO[] appointmentTypes;

        public AppointmentTypeArray() { }

        public AppointmentTypeArray(IList<AppointmentType> mdos)
        {
            if (mdos == null || mdos.Count == 0)
            {
                this.count = 0;
                return;
            }

            this.count = mdos.Count;
            appointmentTypes = new AppointmentTypeTO[mdos.Count];

            for (int i = 0; i < mdos.Count; i++)
            {
                appointmentTypes[i] = new AppointmentTypeTO(mdos[i]);
            }
        }
    }
}