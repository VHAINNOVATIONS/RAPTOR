using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    [Serializable]
    public class AppointmentTypeTO : AbstractTO
    {
        public bool active;
        public string description;
        public string id;
        public string name;
        public string synonym;

        public AppointmentTypeTO() { }

        public AppointmentTypeTO(AppointmentType mdo)
        {
            active = mdo.Active;
            description = mdo.Description;
            id = mdo.ID;
            name = mdo.Name;
            synonym = mdo.Synonym;
        }
    }
}