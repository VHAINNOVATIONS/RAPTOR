using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class Surrogate : BaseModel
    {
        private Int64 _surrogateId;

        public Int64 SurrogateId
        {
            get { return _surrogateId; }
            set { _surrogateId = value; }
        }
        private User _smsUser;

        public User SmsUser
        {
            get { return _smsUser; }
            set { _smsUser = value; }
        }
        //private ParticipantTypeEnum _surrogateType;

        //public ParticipantTypeEnum SurrogateType
        //{
        //    get { return _surrogateType; }
        //    set { _surrogateType = value; }
        //}
        private DateTime _surrogateStartDate;

        public DateTime SurrogateStartDate
        {
            get { return _surrogateStartDate; }
            set { _surrogateStartDate = value; }
        }
        private DateTime _surrogateEndDate;

        public DateTime SurrogateEndDate
        {
            get { return _surrogateEndDate; }
            set { _surrogateEndDate = value; }
        }
        private bool _surrogateAllDay;

        public bool SurrogateAllDay
        {
            get { return _surrogateAllDay; }
            set { _surrogateAllDay = value; }
        }
        private Int64 _timeZone;

        public Int64 TimeZone
        {
            get { return _timeZone; }
            set { _timeZone = value; }
        }
    }
}
