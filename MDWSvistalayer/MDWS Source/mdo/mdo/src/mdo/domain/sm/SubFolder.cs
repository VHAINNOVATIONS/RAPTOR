using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class SubFolder
    {
        private Int64 _id;

        public Int64 Id
        {
            get { return _id; }
            set { _id = value; }
        }
        private string _name;

        public string Name
        {
            get { return _name; }
            set { _name = value; }
        }
        private int _count;

        public int Count
        {
            get { return _count; }
            set { _count = value; }
        }
        private int _unreadCount;

        public int UnreadCount
        {
            get { return _unreadCount; }
            set { _unreadCount = value; }
        }
    }
}
