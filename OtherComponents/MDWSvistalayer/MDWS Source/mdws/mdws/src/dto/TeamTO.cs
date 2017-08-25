using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class TeamTO : AbstractTO
    {
        public string id = "";
        public string name = "";
        public string pcpName = "";
        public string attendingName = "";

        public TeamTO() { }

        public TeamTO(Team mdo)
        {
            this.id = mdo.Id;
            this.name = mdo.Name;
            this.pcpName = mdo.PcpName;
            this.attendingName = mdo.AttendingName;
        }
    }
}
