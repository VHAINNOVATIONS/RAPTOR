using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class MessagesPage : AbstractPage<Addressee>
    {
        const int _defaultPageSize = 15;
        int _pageSize;

        public MessagesPage()
        {
            _pageSize = _defaultPageSize;
        }

        public int PageSize
        {
            get { return _pageSize; }
            set { _pageSize = value; }
        }
    }
}
