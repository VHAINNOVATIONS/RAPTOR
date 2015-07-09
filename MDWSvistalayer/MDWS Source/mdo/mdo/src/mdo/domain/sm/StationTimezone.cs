using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class StationTimezone : BaseModel
    {
        private string _stationNo;

        public string StationNo
        {
            get { return _stationNo; }
            set { _stationNo = value; }
        }
        private string _timezone;

        public string Timezone
        {
            get { return _timezone; }
            set { _timezone = value; }
        }
    }
}
