using System;
using System.Collections.Generic;
using System.Text;
using System.Net;
using System.Net.Sockets;
using System.Threading;

namespace gov.va.medora.mdo.dao.vista
{
    /// <summary>
    /// MdoSocket extends the System.Net.Sockets.Socket class to overload the Socket.Accept method
    /// to allow a timeout argument. 
    /// </summary>
    public class MdoSocket : Socket
    {
        // This socket will hold the socket returned by the asynchronous accept call
        Socket _socket;
        // This ManualResetEvent is what we use for our timeout - we signal it as done
        ManualResetEvent allDone;

        public MdoSocket(AddressFamily addressFamily, SocketType socketType, ProtocolType protocolType)
            : base(addressFamily, socketType, protocolType) 
        {
            allDone = new ManualResetEvent(false);
        }

        public MdoSocket(SocketInformation socketInformation)
            : base(socketInformation)
        {
            allDone = new ManualResetEvent(false);
        }


        /// <summary>
        /// Extended System.Net.Sockets.Socket.Accept method
        /// </summary>
        /// <param name="timeout">timeout for socket in milliseconds</param>
        public Socket Accept(int timeout)
        {
            allDone.Reset(); // ready our ManualResetEvent
            try
            {
                // start async accept on the socket so not blocking
                IAsyncResult async = this.BeginAccept(new AsyncCallback(OnAcceptance), this);

                // if the OnAcceptance event is fired, allDone.Set() will have been called and this will
                // pass right through - if allDone.Set() was not called, then this method should
                // return false and we will throw an error about connection timeout
                bool cxnComplete = allDone.WaitOne(timeout, false);

                if (!cxnComplete)
                {
                    Socket s = (Socket)async.AsyncState;
                    s.Shutdown(SocketShutdown.Both);
                    s.Close();
                    throw new System.Net.Sockets.SocketException(10060); // 'connection timeout' error code
                }
                return _socket;
            }
            catch (Exception)
            {
                throw; 
            }
        }

        public void OnAcceptance(IAsyncResult async)
        {
            Socket s = (Socket)async.AsyncState; // get the socket from the event
            _socket = s.EndAccept(async); // EndAccept stops the async thread from before and returns the socket
            allDone.Set(); // set the ManualResetEvent so we know this event fired
        }
    }

}
